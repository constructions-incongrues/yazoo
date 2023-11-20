// Images - Replace images that do not load, with a thumbnail

let images=document.querySelectorAll("img")

images.forEach(el => {           
    el.onerror=function(e){
        //console.error(e);
        el.src="/img/thumbnail_no_image.jpg"
    } 
});

//console.log('app.js');
