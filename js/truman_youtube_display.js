function showModalVideo(videoId) {
    
    var modal = jQuery('#modal');
    
    modal.find('iframe').attr('src','http://www.youtube.com/embed/'+ videoId +'?autoplay=1');
    
    modal.modal('show');
    
    modal.on('hidden.bs.modal', function () {
           console.log('testee'); 
           modal.find('iframe').attr('src', '');
        });
}

jQuery(document).ready(function(){
    
        jQuery('.vid a').each(function(){
        
        var anchor = jQuery(this);
        
        anchor.on('click',function(e){
            
            e.preventDefault();
            
            showModalVideo(anchor.attr('href'));
        });
    });
});

