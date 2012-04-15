<?php
define('IFRAME_REQUEST', true);

require_once('./bsmlibrary.php');
require_once('../../../wp-admin/admin.php');

function bsm_insert_photo_form($photo) {
?>

<script type="text/javascript" src="<?php plugins_url(); ?>"></script>

<form id="bsm_photo_form">
  <table class="form-table">
    <input type="hidden" name="bsm_photo_guid" id="bsm_photo_guid" value="<?php echo $photo->getGuid(); ?>" />
    <tbody>
      <tr valign="top">
        <th scope="row">Size</th>
        <td>
          <input type="text" size="60" id="bsm_photo_size" name="bsm_photo_size" value="L" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Photo Title</th>
        <td>
          <input type="text" size="60" id="bsm_photo_title" name="bsm_photo_title" value="<?php echo stripslashes($photo->getTitle()); ?>" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">ALT Text</th>
        <td>
          <input type="text" size="60" id="bsm_photo_alt" name="bsm_photo_alt" value="<?php echo stripslashes($photo->getTitle()); ?>" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Link</th>
        <td>
          <input type="text" size="60" id="bsm_photo_link" name="bsm_photo_link" value="<?php echo $photo->getLinkUrl(); ?>" />
        </td>
      </tr>
    </tbody>
  </table>
  <p>
    <input name="Submit" class="button-primary" type="submit" value="Insert Photo" />
  </p>
</form>

<?php
}

$smugmugOptions = get_option('bsm_options');

if ($smugmugOptions['nickname']) {
  $smugmug = new BSM_Account($smugmugOptions['nickname']);
}
else {
  $smugmug = null;
}

switch ($_GET['cmd']) {
  case 'photo':
    echo '<h1 id="bsmheadline">Photo</h1>';
    $photo = new BSM_Photo(array(
      'title' => $_GET['title'],
      'link'  => $_GET['link'],
      'guid'  => $_GET['guid']
    ));
?>

    <img src="<?php echo $photo->getImageUrl('Th'); ?>" />

<?php
    bsm_insert_photo_form($photo);
    break;
  
  case 'gallery':
    echo '<h1 id="bsmheadline">Choose a Photo</h1>';
    $gallery = new BSM_Gallery($_GET['albumId'], $_GET['albumKey']);
    foreach ($gallery as $photo) {
      $url = sprintf(
        "%s?cmd=photo&title=%s&link=%s&guid=%s",
        $_SERVER['PHP_SELF'],
        urlencode($photo->getTitle()),
        urlencode($photo->getLinkUrl()),
        urlencode($photo->getGuid())
      );
?>
      <a href="<?php echo $url; ?>" class="thickbox" onclick="return false;">
        <img src="<?php echo $photo->getImageUrl('Ti'); ?>" />
      </a>
<?php
    }
    break;
  
  default:
    if (!is_null($smugmug)) {
      echo '<h1 id="bsmheadline">Choose a Gallery</h1>';
      foreach ($smugmug as $gallery) {
        $photo = $gallery->next();
        $url = sprintf(
          "%s?cmd=gallery&albumId=%s&albumKey=%s",
          $_SERVER['PHP_SELF'],
          $gallery->getAlbumId(),
          $gallery->getAlbumKey()
        );
?>
        <div style="display:inline-block;width:100px;height:150px;margin: 0px 5px 5px 0px;">
          <a href="<?php echo $url; ?>" class="thickbox" onclick="return false;">
            <img src="<?php echo $photo->getImageUrl('Ti'); ?>" />
          </a>
          <a href="<?php echo $url; ?>" class="thickbox" onclick="return false;">
            <strong>
              <?php echo $gallery->getTitle(); ?>
            </strong>
          </a>
          <br />
          <?php echo $gallery->numPhotos(); ?> photos
        </div>
<?php
      }
    }
    else {
      echo '<h1 id="bsmheadline">Blog SmugMug</h1>';
      echo '<p>The Blog SmugMug plug-in is not configured!</p>';
    }
    break;
}