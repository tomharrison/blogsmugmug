<?php
// Format in which to get SmugMug feeds; "rss200" or "atom03"
define('BSM_FORMAT', 'rss200');

abstract class BSM_Consumable implements Iterator {
  protected $members; // Array of values to be iterated.
  protected $doc;     // Instance of DOMDocument for the consumable feed.
  protected $xpath;   // Instance of DOMXPath, for querying the feed.
  
  public function __construct($feedUrl) {
    $this->members = array();
    
    $this->doc = new DOMDocument();
    $this->doc->load($feedUrl);
    $this->xpath = new DOMXPath($this->doc);
    
    $this->parseFeed();
  }
  
  function rewind() {
    return reset($this->members);
  }
  
  function current() {
    return current($this->members);
  }
  
  function key() {
    return key($this->members);
  }
  
  function next() {
    return next($this->members);
  }
  
  function valid() {
    return key($this->members) !== NULL;
  }
  
  function size() {
    return count($this->members);
  }
  
  /**
   * Child classes should implement this function in order to parse gallery and
   * photo data out of SmugMug's XML feeds. It's the last thing run by 
   * the constructor.
   */
  abstract protected function parseFeed();
}

// A SmugMug photo.
class BSM_Photo {
  private $title; // Title of the photo.
  private $link;  // Link to SmugMug permalink page for the photo.
  private $guid;  // Unique identifier at SmugMug.

  public function __construct($params = NULL) {
    if (is_array($params)) {
      foreach ($params as $k => $v)
        if (property_exists('BSM_Photo', $k)) 
          $this->$k = $v;
    }
  }

  /**
  * Get the URL for a photo's image file at a given size. Examples:
  *
  * Pre-defined SmugMug sizes: Th, Ti, S, M, L, XL, XL2, XL3
  * Any given dimensions, as a string: "640x640"
  *
  * SmugMug will always retain aspect ratio when resizing to arbitrary
  * dimensions.
  */
  public function getImageUrl($size) {
    if (preg_match('/^(.+?)\/Th\/(.+?)-Th\.([a-z]+)$/i', $this->guid, $m)) {
      return sprintf("%s/%s/%s-%s.%s", $m[1], $size, $m[2], $size, $m[3]);
    }
    elseif (preg_match('/^(.+?)-Th\.([a-z]+)$/i', $this->guid, $m)) {
      return sprintf("%s-%s.%s", $m[1], $size, $m[2]);
    }
    else {
      return null;
    }
  }

  /**
   * Get a URL to a page at SmugMug showing the photo. If the $size parameter
   * is not given, the URL to the photos' gallery page will be returned. If the
   * size parameter is given, a URL to a lightbox page showing the photo will be
   * returned.
   */
  public function getLinkUrl($size = NULL) {
    return $this->link;
  }
  
  // Get the photos title at SmugMug.
  public function getTitle() {
    return $this->title;
  }
  
  public function getGuid() {
    return $this->guid;
  }
}

/**
 * A SmugMug gallery. Iterate of an instance of this to traverse the photos
 * contained within.
 */
class BSM_Gallery extends BSM_Consumable {
  // Combine the following two in order to generate a feed or gallery URL.
  private $albumId;
  private $albumKey;
  
  private $title;     // Title of the gallery.
  private $link;      // URL to the gallery.

  public function __construct($albumId, $albumKey) {
    $this->albumId   = $albumId;
    $this->albumKey  = $albumKey;
    
    $feedUrl = sprintf(
      "http://api.smugmug.com/hack/feed.mg?Type=gallery&Data=%s_%s&format=%s",
      $this->albumId,
      $this->albumKey,
      BSM_FORMAT
    );
    
    parent::__construct($feedUrl);
  }
  
  public function getTitle() {
    return $this->title;
  }
  
  public function getLink() {
    return $this->link;
  }
  
  public function numPhotos() {
    return $this->size();
  }
  
  public function getAlbumId() {
    return $this->albumId;
  }
  
  public function getAlbumKey() {
    return $this->albumKey;
  }
  
  // Extract photos and gallery meta data from its XML feed.
  protected function parseFeed() {
    $doc   = $this->doc;
    $xpath = $this->xpath;
    
    $this->title = $xpath->query('//rss/channel/title')->item(0)->nodeValue;
    $this->link  = $xpath->query('//rss/channel/link')->item(0)->nodeValue;
    
    $items = $xpath->query('//rss/channel/item');
    foreach ($items as $item) {
      $photo = new BSM_Photo(array(
        'title' => $item->getElementsByTagName('title')->item(0)->nodeValue,
        'link'  => $item->getElementsByTagName('link')->item(0)->nodeValue . '&lb=1&s=A',
        'guid'  => $item->getElementsByTagName('guid')->item(0)->nodeValue
      ));
      $this->members[] = $photo;
    }
  }
}

/**
 * A SmugMug account. Iterate over an instance of this to traverse the
 * galleries contained within.
 */ 
class BSM_Account extends BSM_Consumable {
  private $nickname;
  private $baseUrl;
  
  public function __construct($nickname) {
    $this->nickname = $nickname;
    
    $feedUrl = sprintf(
      "http://api.smugmug.com/hack/feed.mg?Type=nickname&Data=%s&format=%s",
      $this->nickname,
      BSM_FORMAT
    );
    
    parent::__construct($feedUrl);
  }
  
  // Extract galleries from XML feed.
  protected function parseFeed() {
    $doc   = $this->doc;
    $xpath = $this->xpath;
    $items = $xpath->query('//rss/channel/item');
    foreach ($items as $item) {
      $guid = $item->getElementsByTagName('guid')->item(0)->nodeValue;
      if (preg_match('/\/([a-z0-9]+)_([a-z0-9]+)\/?$/i', $guid, $m)) {
        $this->members[] = new BSM_Gallery($m[1], $m[2]);
      }
    }
  }
}