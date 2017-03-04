# REST API v1
teendevops has a REST API on the web for developers to create clients with.
## Base URL
 - http://teendevops.net/api/v1/

## Endpoints
### GET
 - GET [channels/get/](#channels/get/)
 - GET [chat/get/](#chat/get/)
 - GET [users/get/](#users/get/)
 - GET [users/findsimilar/](#users/findsimilar/)

### POST
 - POST [chat/send/](#chat/send/)

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
         "creator":""
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
   "channel":"1",
   "limit":"2",
   "chat":[
      {
         "username":"spamaccount",
         "timestamp":"2017-03-04 01:27:33",
         "channel":"1",
         "message":"hey\n",
         "deleted":"false",
         "message_id":1
      },
      {
         "username":"spamaccount",
         "timestamp":"2017-03-04 01:27:40",
         "channel":"1",
         "message":"how r u\n",
         "deleted":"false",
         "message_id":2
      }
   ]
}
```


### GET <a name="users/get/"></a>users/get/
Returns a list of users

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
         "name":0,
         "banned":"false",
         "description":"Write something about yourself here...",
         "location":"cat location > \/dev\/null",
         "language":"Java",
         "rank":"1"
      },
      {
         "id":3,
         "username":"mooncat39",
         "name":0,
         "banned":"false",
         "description":"I am mooncat39. hi",
         "location":"Oregon",
         "language":"Java",
         "rank":"0"
      }
   ]
}
```


### POST <a name="chat/send/"></a> chat/send/
Sends a chat message

**Parameters:**

| Name      | Required | Description                           | Default | Example      |
|-----------|----------|---------------------------------------|---------|--------------|
| sessionid | true     | The user's session token              |         |              |
|      csrf | true     | The user's csrf token                 |         |              |
|   channel | true     | The channel ID to send the message to |         | 1            |
|   message | true     | The message to send to the channel    |         | Hello world! |
|    format | false    | The format to return the data in      | json    | dump         |


**Sample Request:**
POST http://teendevops.net/api/v1/chat/send/?sessionid=e414c58b9a7e6b6c2071e1f52fede75f&csrf=a434c58bfac855a1a071e1f52fdde12f&msg=hi+guys&channel=1

**Sample Response:**
```
{
   "success":true
}
```
