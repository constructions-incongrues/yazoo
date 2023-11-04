# Notes


## TODO
- Check domain using Symfony validator
- What to do with `Myspace` ?
- Comment est ce qu'on gere les 404, et les `page not found` qui ne sont pas des 404 ? -> On analyse le titre et on avise.


## Dead Domains
- http://sphotos.ak.fbcdn.net


## Comments Stats

posted comments per year
```SELECT YEAR(DateCreated) as year, COUNT(*) AS comments FROM `LUM_Comment` WHERE 1 GROUP BY year ORDER BY `year` ASC; ```

posted comments per thread

SELECT DiscussionID as thread, COUNT(*) AS comments FROM `LUM_Comment` WHERE 1 GROUP BY thread ORDER BY `comments` DESC;