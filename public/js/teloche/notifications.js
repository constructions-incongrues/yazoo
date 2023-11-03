//https://docs.joinpeertube.org/api-rest-reference.html

class notifications{

    #URL='https://teloche.jeancloude.club';

    #data=[];//API response

    #token=null;

    constructor(){
        this.authenticate();
        console.log('/teloche','notifications.js');
    }



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



    getNotifications(){

        console.log('getNotifications()', this.#token);

        const apiUrl = `${this.#URL}/api/v1/users/me/notifications?count=100`;

            fetch(apiUrl, {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${this.#token}`, // Replace YOUR_ACCESS_TOKEN with your actual access token
                    "Content-Type": "application/json",
                },
            }).then(function(response) {
                return response.json()
            }).then((data)=>{
                console.log(data)
                this.#data=data;
                this.displayNotifications();
            })
    }

    notificationTypes={
        7:'videoImport',
        10:'actorFollow',
    };


    displayNotifications(){

        console.log('displayNotifications()');

        let head=document.querySelector('#boxNotifications>div.card-header');
        let body=document.querySelector('#boxNotifications>div.card-body');

        body.innerHTML = '';// Clear

        let htm='<table class="table table-hover">';
        htm+='<thead>';
        htm+='<th width=30>id</th>';
        htm+='<th width=30>Type</th>';
        htm+='<th width=30>Read</th>';
        htm+='<th class="text-end">Created at</th>';

        htm+='</thead>';
        htm+='<tbody>';

        this.#data.data.forEach(o=>{
            console.log(o);
            htm+='<tr>';
            htm+='<td><i class="text-muted">' + o.id;
            htm+='<td><i class="text-muted">' + o.type;
            htm+='<td>' + o.read;
            htm+='<td class="text-end"><i class="text-muted">' + o.createdAt;
        });
        htm+='</tbody>';
        htm+='</table>';

        head.innerText=`${this.#data.total} notification(s)`;
        if(this.#data.data.length==0){
            body.innerHTML='<pre>no notification</pre>';
        }else{
            body.innerHTML=htm;
        }

    }
}

new notifications();