# REST API v1
This file contains the documentation for teendevops' REST API version 1.

## Useful Information
### Rate Limiting
The REST API implements rate limiting to prevent spam and abuse. The default rate limit is **600 requests per hour per IP**. Contact info@teendevops.net to adjust this limit.

Attempts to evade this limit will using methods including (but not limited to) the use of proxies or ip spoofing will result in a ban from the use of the REST API.

### Error Handling
When an error occurs on the server, the variable `success` will be set to `false`. Otherwise, `success` will be set to `true`. The variable `error` will always contain the error message, if any.

Here is a list of the most common contents of `error` and their meanings:
 - `Request method must be POST.` - This means that the request method POST must be used. GET, PUT, DELETE, or any others are not allowed.
 - `Parameter '...' is not set.` - This means that the GET or POST parameter `...` is required, and must be set.
 - `Parameter '...' is not valid.` - This means that the GET or POST parameter `...` is not valid. For example, trying to put a username where the user id belongs won't work.
 - `Invalid CSRF token.` - This means that the GET or POST parameter `csrf` is either not set, or is not valid. The CSRF token can be obtained by making a GET request to [auth/csrf/](#auth/csrf/).

If you are confused about any of the messages stored in `error`, feel free to shoot an email to info@teendevops.net.

### Output Formatting
The output format of any of the endpoints can be configured by setting the parameter `format`. The default format is [json](https://www.w3schools.com/js/js_json_intro.asp).

Valid settings for the parameter `format` include:
 - `json` - [JavaScript Object Notation](https://www.w3schools.com/js/js_json_intro.asp)
 - `xml` - [Extensible Markup Language](https://www.w3schools.com/xml/)
 - `dump` - [PHP Array Dump](http://php.net/manual/en/function.print-r.php)

## Base URL
 - http://teendevops.net/api/v1/

## Endpoints
### GET
 - GET [channels/get/](#channels/get/)
 - GET [chat/get/](#chat/get/)
 - GET [users/get/](#users/get/)
 - GET [users/findsimilar/](#users/findsimilar/)
 - GET [auth/csrf/](#auth/csrf/)

### POST
 - POST [chat/send/](#chat/send/)
 - POST [auth/login/](#auth/login/)

## Documentation
### GET <a name="channels/get/"></a>channels/get/
Returns a list of channels

**Parameters:**

| Name    | Required | Description                                      | Default | Example |
|---------|----------|--------------------------------------------------|---------|---------|
|  format | false    | The format to return the data in                 | json    | dump    |

**Sample Request:**
GET http://teendevops.net/api/v1/channels/get/?format=json

**Sample Response:**
```
{
   "success":true,
   "channels":[
      {
         "id":1,
         "title":"offtopic",
         "description":"A channel for 1337 h@xX0rz",
         "creator":"Arinerron"
      },
      {
         "id":2,
         "title":"hackers",
         "description":"Just testing",
         "creator":"Arinerron"
      },
      {
         "id":3,
         "title":"ontopic",
         "description":"A channel for 1337 h@xX0rz",
         "creator":"Arinerron"
      }
   ]
}
```


### GET <a name="chat/get/"></a>chat/get/
Returns the latest chat messages

**Parameters:**

| Name    | Required | Description                                      | Default | Example |
|---------|----------|--------------------------------------------------|---------|---------|
| channel | true     | The channel ID to fetch the messages from        |         | 1       |
|   limit | false    | The maximum number of messages to fetch MAX:2500 | 1000    | 2500    |
|  format | false    | The format to return the data in                 | json    | dump    |

**Sample Request:**
GET http://teendevops.net/api/v1/chat/get/?channel=1&limit=2&format=json

**Sample Response:**
```
{
   "success":true,
   "channel":1,
   "limit":2,
   "chat":[
      {
         "username":"spamaccount",
         "timestamp":"2017-03-04 01:27:33",
         "channel":1,
         "message":"hey\n",
         "deleted":false,
         "message_id":1
      },
      {
         "username":"spamaccount",
         "timestamp":"2017-03-04 01:27:40",
         "channel":1,
         "message":"how r u\n",
         "deleted":false,
         "message_id":2
      }
   ]
}
```


### GET <a name="users/get/"></a>users/get/
Returns the details about a user

**Parameters:**

| Name     | Required    | Description                      | Default | Example   |
|----------|-------------|----------------------------------|---------|-----------|
|       id | or username | The user to get the details of   |         | 1         |
| username | or id       | The user to get the details of   |         | Arinerron |
|   format | false       | The format to return the data in | json    | dump      |


**Sample Request:**
GET http://teendevops.net/api/v1/users/get/?username=Arinerron&format=json

**Sample Response:**
```
{
   "success":true,
   "user":{
      "id":1,
      "username":"Arinerron",
      "rank":2,
      "banned":false,
      "description":"I'm some random guy. Nah, I'm just too lazy to write a good tagline.",
      "languages":"Java",
      "location":"Oregon, USA",
      "icon":"\/assets\/user-icons\/97ddfbefd0638f31f6c442552f379d05.png"
   }
}
```


### GET <a name="users/findsimilar/"></a> users/findsimilar/
Returns a list of similar users

**Parameters:**

| Name     | Required | Description                             | Default | Example   |
|----------|----------|-----------------------------------------|---------|-----------|
| language | true     | The language to use to search for users |         | Java      |
|   format | false    | The format to return the data in        | json    | dump      |


**Sample Request:**
GET http://teendevops.net/api/v1/users/findsimilar/?language=Java&format=json

**Sample Response:**
```
{
   "success":true,
   "users":[
      {
         "id":1,
         "username":"Arinerron",
         "banned":false,
         "description":"Write something about yourself here...",
         "location":"cat location > \/dev\/null",
         "language":"Java",
         "rank":1,
         "icon":"\/assets\/user-icons\/97ddfbefd0638f31f6c442552f379d05.png"
      },
      {
         "id":3,
         "username":"mooncat39",
         "banned":false,
         "description":"I am mooncat39. hi",
         "location":"Oregon",
         "language":"Java",
         "rank":0,
         "icon":"\/assets\/user-icons\/default.png"
      }
   ]
}
```


### GET <a name="auth/csrf/"></a> auth/csrf/
Returns the session token and the csrf token

If `teendevops_session` cookie is set, it will return the csrf token for that sessionid. If it is not set, it will generate a sessionid and csrf token.

**Parameters:**

| Name        | Required | Description                                                   | Default | Example |
|-------------|----------|---------------------------------------------------------------|---------|---------|
|      format | false    | The format to return the data in                              | json    | dump    |


**Sample Request:**
GET http://teendevops.net/api/v1/auth/csrf/?format=json

**Sample Response:**
```
{
   "success":true,
   "sessionid":"8cma7c63bde0f7i2nsj87753d2",
   "csrf":"6e2bfb1006713403eb88e998c189c607"
}
```


### POST <a name="chat/send/"></a> chat/send/
Sends a chat message

Requires `teendevops_session` cookie to be set.

**Parameters:**

| Name      | Required | Description                           | Default | Example      |
|-----------|----------|---------------------------------------|---------|--------------|
|      csrf | true     | The user's csrf token                 |         |              |
|   channel | true     | The channel ID to send the message to |         | 1            |
|   message | true     | The message to send to the channel    |         | Hello world! |
|    format | false    | The format to return the data in      | json    | dump         |


**Sample Request:**
POST http://teendevops.net/api/v1/chat/send/?csrf=a434c58bfac855a1a071e1f52fdde12f&message=hi+guys&channel=1

Cookie: `sessionid=e414c58b9a7e6b6c2071e1f52fede75f;`

**Sample Response:**
```
{
   "success":true,
   "channel":1,
   "username":Arinerron,
   "message":"hi guys"
}
```

### POST <a name="auth/login"></a> auth/login/
Authenticates the session id sent via cookies

Requires `teendevops_session` cookie to be set. Will return a brand new authenticated sessionid and csrf token to use. This is to prevent [session fixation](https://www.owasp.org/index.php/Session_fixation).

**Parameters:**

| Name      | Required | Description                                                | Default | Example      |
|-----------|----------|------------------------------------------------------------|---------|--------------|
|  username | true     | The username or email of the account                       |         | testuser     |
|  password | true     | The password of the account                                |         | testpassword |
|      csrf | true     | The user's csrf token                                      |         |              |


**Sample Request:**
POST http://teendevops.net/api/v1/chat/send/?username=testuser&password=testpassword&csrf=a434c58bfac855a1a071e1f52fdde12f

Cookie: `sessionid=e414c58b9a7e6b6c2071e1f52fede75f;`

**Sample Response:**
```
{
   "success":true,
   "sessionid":"tj0fkeh6l0vmt2u0dr7sqc3uh6",
   "csrf":"fc8e1fabf22dac6589cfa261ad643512"
}
```
