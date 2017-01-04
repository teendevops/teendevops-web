SELECT * FROM `messages`
RIGHT JOIN `users`
ON `messages.id`=`users.id`
ORDER BY `messages.id` 
LIMIT 100;
