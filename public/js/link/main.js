// Link Preview using oembed

// It would be nice to avoid fetching preview for links with a `Yazoo preview`

let id=+document.getElementById("id").value;
let el=document.getElementById('preview2');
el.innerHTML="<i>fetching preview...</i>";

let url=`/api/link/${id}/embed`
console.log(`fetching link#${id} preview...`, url);

fetch(url)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Work with the JSON data
        el.innerHTML='';
        console.log(data); // Output the JSON data to the console
        if(data.code){
            console.log(data.code);
            el.innerHTML=data.code;
        }else{
            console.log("No preview html code")
        }

    })
    .catch(error => {
        console.error('There has been a problem with your fetch operation:', error);
        el.innerHTML=error;
    });

