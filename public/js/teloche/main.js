//https://docs.joinpeertube.org/api-rest-reference.html

let URL='https://teloche.jeancloude.club';

let access_token=null;
let account_name='yazoo';
let notifications=[];
let channels=[];
let videos=[];
let runner_jobs=[];


function authenticate(){//retrieve token

    console.log('authenticate()');
    //https://peertube2.cpy.re/api/v1/oauth-clients/local

    fetch(`/api/teloche/authenticate`)
        .then(function(response) {
            return response.json()
        }).then(function(data) {
            console.log("tada", data);
            access_token=data.access_token;
            getNotifications(access_token)

        })
}

authenticate();


function getNotifications(access_token){

    console.log('getNotifications(access_token)', access_token);

    const apiUrl = `${URL}/api/v1/users/me/notifications`;

        fetch(apiUrl, {
            method: "GET",
            headers: {
                Authorization: "Bearer "+access_token, // Replace YOUR_ACCESS_TOKEN with your actual access token
                "Content-Type": "application/json",
            },
        }).then(function(response) {
            return response.json()
        }).then(function(data) {
            console.log(data)
            notifications=data.data;
            displayNotifications();
        })
}



// List video channels of an account

function getChannels(account_name){

    console.log(`getChannels(${account_name})`)

    fetch(`${URL}/api/v1/accounts/${account_name}/video-channels`)
        .then(function(response) {
            return response.json()
        }).then(function(data) {
            console.log(data)
            channels=data.data;
            displayChannels();
        })
}

function getVideos(account_name){

    console.log(`getVideos(${account_name})`)
    //List videos of an account
    fetch(URL+`/api/v1/accounts/${account_name}/videos`)
        .then(function(response) {
            return response.json()
        }).then(function(data) {
            //console.log(data)
            videos=data.data;
            displayVideos();
        })
}

function listVideos()
{
    // List videos (with search)
    // https://docs.joinpeertube.org/api-rest-reference.html#tag/Video/operation/getVideos

    fetch(URL+`/api/v1/videos?count=3`)
        .then(function(response) {
            return response.json()
        }).then(function(data) {
            console.log('listVideos()', data)
            //videos=data.data;
            //displayVideos();
        })
}


function displayChannels(){
    console.log('displayChannels()');
    let box=document.querySelector('#boxChannels>div.card-body');


    box.innerHTML = '';// Clear

    let htm='<table class="table table-hover">';
    htm+='<thead>';
    htm+='<th>id</th>';
    htm+='<th>Channel</th>';
    htm+='<th>name</th>';
    htm+='</thead>';
    htm+='<tbody>';
    channels.forEach(o=>{
        //console.log(o);
        htm+='<tr>';
        htm+='<td><i class="text-muted">' + o.id;
        htm+='<td>' + o.displayName;
        htm+=` <a href="${o.url}">url</a>`;
        htm+='<td><i class="text-muted">' + o.name + "</i>";
    });
    htm+='</tbody>';
    htm+='</table>';

    box.innerHTML=htm;
}

function displayVideos(){
    console.log('displayVideos()');
    let box=document.querySelector('#boxVideos>div.card-body');
    box.innerHTML = '';// Clear

    let htm='<table class="table table-hover">';
    htm+='<thead>';
    htm+='<th>id</th>';
    htm+='<th>Name</th>';
    htm+='<th>Privacy</th>';
    htm+='</thead>';
    htm+='<tbody>';
    videos.forEach(o=>{
        console.log(o);
        htm+='<tr>';
        htm+='<td><i class="text-muted">' + o.id;
        htm+='<td><a href="'+o.url+'">' + o.name;
        htm+='<td><i class="text-muted">' + o.privacy.label + "</i>";
    });
    htm+='</tbody>';
    htm+='</table>';
    if(videos.length==0){
        box.innerHTML='<pre>no video</pre>';
    }else{
        box.innerHTML=htm;
    }
}

function displayNotifications()
{
    console.log('displayNotifications()');
    let box=document.querySelector('#boxNotifications>div.card-body');

    box.innerHTML = '';// Clear

    let htm='<table class="table table-hover">';
    htm+='<thead>';
    htm+='<th>id</th>';
    htm+='<th>Type</th>';
    htm+='<th>Name</th>';

    htm+='</thead>';
    htm+='<tbody>';
    notifications.forEach(o=>{
        console.log(o);
        htm+='<tr>';
        htm+='<td><i class="text-muted">' + o.id;
        htm+='<td><i class="text-muted">' + o.type;
        htm+='<td><a href="'+o.url+'">' + o.name;
        htm+='<td><i class="text-muted">' + o.created_at;
    });
    htm+='</tbody>';
    htm+='</table>';
    if(notifications.length==0){
        box.innerHTML='<pre>no notification</pre>';
    }else{
        box.innerHTML=htm;
    }

}

getChannels(account_name);
getVideos(account_name);
//getRunnerJobs();


//Notifications
/*
function notifyMe() {
    if (!("Notification" in window)) {
      // Check if the browser supports notifications
      alert("This browser does not support desktop notification");
    } else if (Notification.permission === "granted") {
      // Check whether notification permissions have already been granted;
      // if so, create a notification
      const notification = new Notification("Hi there!");
      // …
    } else if (Notification.permission !== "denied") {
      // We need to ask the user for permission
      Notification.requestPermission().then((permission) => {
        // If the user accepts, let's create a notification
        if (permission === "granted") {
          const notification = new Notification("Hi there!");
          // …
        }
      });
    }

    // At last, if the user has denied notifications, and you
    // want to be respectful there is no need to bother them anymore.
  }
notifyMe();
*/
console.log('/teloche','main.js');