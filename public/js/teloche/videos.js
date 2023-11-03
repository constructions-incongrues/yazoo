//https://docs.joinpeertube.org/api-rest-reference.html

class videos{

    #URL='https://teloche.jeancloude.club';

    #data=[];//API response

    //#token=null;

    constructor(){
        //this.authenticate();
        this.getVideos();
        console.log('/teloche','videos.js');
    }


    /*
    authenticate(){//retrieve token

        console.log('authenticate()');

        fetch(`/api/teloche/authenticate`)
            .then(function(response) {
                return response.json()
            }).then((data)=>{
                console.log(data);
                this.#token=data.access_token;
                this.getNotifications();
            })
    }
    */


    getVideos()
    {
        // List videos (with search)
        // https://docs.joinpeertube.org/api-rest-reference.html#tag/Video/operation/getVideos

        fetch(`${this.#URL}/api/v1/videos?channel=13`)
            .then(function(response) {
                return response.json()
            }).then((data)=>{
                //console.log('listVideos()', data)
                this.#data=data;
                this.displayVideos();
            })
    }


    displayVideos(){

        let head=document.querySelector('#boxVideos>div.card-header');
        let body=document.querySelector('#boxVideos>div.card-body');

        body.innerHTML = '';// Clear

        let htm='<table class="table table-hover">';
        htm+='<thead>';
        htm+='<th width=30>id</th>';
        //htm+='<th width=30>Type</th>';
        htm+='<th>Name</th>';
        htm+='<th class="text-end">Created at</th>';

        htm+='</thead>';
        htm+='<tbody>';

        this.#data.data.forEach(o=>{
            console.log(o);
            htm+='<tr>';
            htm+='<td><i class="text-muted">' + o.id;
            //htm+='<td><i class="text-muted">' + o.type;
            htm+='<td>' + o.name;
            htm+=` <a href="${o.url}">url</a>`;
            htm+='<td class="text-end"><i class="text-muted">' + o.createdAt;
        });
        htm+='</tbody>';
        htm+='</table>';

        head.innerText=`${this.#data.total} video(s)`;
        if(this.#data.data.length==0){
            body.innerHTML='<pre>no data</pre>';
        }else{
            body.innerHTML=htm;
        }

    }
}

new videos();