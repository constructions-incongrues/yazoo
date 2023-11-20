

let images=document.querySelectorAll("img")

images.forEach(el => {
    //console.log("el",el);
    
    el.onabort=function(e){
        console.log("abort",e);
    }
    
    el.onerror=function(e){
        console.error(e);
        el.src="/img/thumbnail_no_image.jpg"
    }
    
});


console.log('app.js?', images);
