$(function(){
    $('#file').change(function(e){
        var $this = $(this);
        var file = $this[0].files[0];

        if(file.type != 'image/jpeg' && file.type != 'image/png'){
            alert('Vous devez sélectionner une image PNG ou JPG');
            $this.prev().html('Sélectionnez une image');
            $('#send').attr('disabled', '');
        }
        else{
            $this.prev().html(file.name);
            $('#send').removeAttr('disabled');
            var fr = new FileReader();
            fr.readAsDataURL(file);
            fr.onload = function(e) {
                $('#img').cropper("setImgSrc", this.result);
            };
        }
    });
});