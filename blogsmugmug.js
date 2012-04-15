var BlogSmugMug = {
  addGuidCustomField: function(guid) {
    var guidOptions = jQuery('#metakeyselect').find('option[value="BSM Photo GUID"]');
    if (guidOptions.length > 0) {
      jQuery('#metakeyinput').hide();
      jQuery('#metakeyselect').show().val('BSM Photo GUID');
    }
    else {
      jQuery('#metakeyselect').hide();
      jQuery('#metakeyinput').show().val('BSM Photo GUID');
    }
    jQuery('#metavalue').val(guid);
    jQuery('#addmetasub').click();
  },

  imageUrl: function(guid, size) {
    var 
      regexp = /^(.+?)\/Th\/(.+?)-Th\.([A-Za-z]+)$/,
      result = regexp.exec(guid);
    
    if (result != null) {
      return result[1] + '/' + size + '/' + result[2] + '-' + size + '.' + result[3];
    }
    
    var
      regexp = /^(.+?)-Th\.([a-z]+)$/,
      result = regexp.exec(guid);
    
    if (result != null) {
      return result[1] + '-' + size + '.' + result[2];
    }
    
    return null;
  },
  
  chosenPhotoData: function() {
    return {
      guid: jQuery('#bsm_photo_guid').val(),
      title: jQuery('#bsm_photo_title').val(),
      size: jQuery('#bsm_photo_size').val(),
      link: jQuery('#bsm_photo_link').val()
    };
  },
      
  /**
   * http://stackoverflow.com/questions/1064089/inserting-a-text-where-cursor-is-using-javascript-jQuery
   */
  insertPhotoIntoEditor: function(imageUrl, alt, link) {
    var 
      markup = '<a href="' + link + '"><img src="' + imageUrl + '" alt="' + alt + '" /></a>',
      element = document.getElementById('content');
    if (document.selection) {
        element.focus();
        var sel = document.selection.createRange();
        sel.text = markup;
        element.focus();
    } else if (element.selectionStart || element.selectionStart === 0) {
        var startPos = element.selectionStart;
        var endPos = element.selectionEnd;
        var scrollTop = element.scrollTop;
        element.value = element.value.substring(0, startPos) + markup + element.value.substring(endPos, element.value.length);
        element.focus();
        element.selectionStart = startPos + markup.length;
        element.selectionEnd = startPos + markup.length;
        element.scrollTop = scrollTop;
    } else {
        element.value += markup;
        element.focus();
    }
  }
};

jQuery(document).ready(function() {
  jQuery('body').delegate('#bsm_photo_form', 'submit', function() {
    var 
      photo = BlogSmugMug.chosenPhotoData(),
      imgUrl = BlogSmugMug.imageUrl(photo.guid, photo.size);
      
    BlogSmugMug.insertPhotoIntoEditor(imgUrl, photo.title, photo.link); 
    BlogSmugMug.addGuidCustomField(photo.guid);
    
    tb_remove();
    
    return false;
  });
});