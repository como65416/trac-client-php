# Trac Client (PHP)

a library used for control `The Trac Project` ticket

----

## Requirements

- support : `The Trac Project` 1.0.* ~ 1.2.*  ( >= 1.3 not tested)
- trac need install `Trac XML-RPC Plugin` (https://trac-hacks.org/wiki/XmlRpcPlugin)

----

## Example code

```php
<?php

use Comoco\TracClientPhp\Client as TracClient;

$api_url = "http://trac.local/login/jsonrpc";
$username = '<your username>';
$password = '<your password>';

$tracClient = new tracClient($api_url, $username, $password);
$ticket_id = $tracClient->createTicket('my first ticket', 'ticket content', [
    'owner' => 'bob',
    'cc' => 'alice, web',
    'priority' => 'minor'
]);
$tracClient->uploadAttachment($ticket_id, 'example.xml', 'is a example file', "/tmp/example.xml");
$tracClient->addComment($ticket_id, 'It is great!');
$tracClient->resolveTicket($ticket_id, 'ok', 'fixed')

```

----

## Class Function


### getUserTicketIds($username, array $statuses, $limit = 100)

get user own ticket ids

- `$statuses` avaliable status could not same on different trac system (based on system setting)

```php
$ticket_ids = $tracClient->getUserTicketIds('bob', ['accepted', 'assigned'], 50);
```

### getTicketInfo($ticket_id)

get ticket information

- `return data`  could not same on different trac system (based on installed module)

```php
$ticket_id = 1;
$ticket_info = $tracClient->getTicketInfo($ticket_id);
```

### createTicket($summary, $description = '', array $attributes = [])

- `$attributes` could not same on different trac system (based on installed module)

```php
$ticket_id = $tracClient->createTicket('my first ticket', 'it is a example ticket', [
    'owner' => 'bob',
    'cc' => 'alice, jack',
    'priority' => 'minor'
]);
```
### updateTicket($ticket_id, $comment = '', array $attributes = [])

update ticket information

- `$attributes` could not same on different trac system (based on installed module)

```php
$ticket_id = 1;
$tracClient->updateTicket($ticket_id, 'change ticket content', [
    'summary' => 'my first ticket v2',
    'description' => 'it is a example ticket v2',
    'cc' => 'alice, jack, ellen'
]);
```

### acceptTicket($ticket_id, $comment = '')

accept the ticket

```php
$ticket_id = 1;
$tracClient->acceptTicket($ticket_id, 'accept the ticket');
```

### reassignUser($ticket_id, $username, $comment = '')

reassign ticket to the user

```php
$ticket_id = 1;
$tracClient->reassignUser($ticket_id, 'alice', 'assign ticket to alice');
```

### resolveTicket($ticket_id, $comment = '', $option = 'fixed')

resolve the ticket

- `$option` avaliable option could not same on different trac system  (it is different based on trac setting)

```php
$ticket_id = 1;
$tracClient->resolveTicket($ticket_id, 'close ticket', 'fixed');
```

### reopenTicket($ticket_id, $comment = '')

reopen the ticket

```php
$ticket_id = 1;
$tracClient->reopenTicket($ticket_id, 'reopen the ticket');
```

### deleteTicket($ticket_id)

delete the ticket

```
$ticket_id = 1;
$tracClient->deleteTicket($ticket_id);
```

### addComment($ticket_id, $comment)

add comment

```
$ticket_id = 1;
$tracClient->addComment($ticket_id, 'it is great');
```

### getComments($ticket_id)

get comments

```php
$ticket_id = 1;
$comments = $tracClient->getComments($ticket_id);
```

### listAttachments($ticket_id)

list the ticket's attachments

```php
$ticket_id = 1;
$attachments = $tracClient->listAttachments($ticket_id);
```

### uploadAttachment($ticket_id, $filename, $description, $upload_fila_path)

upload a attachment to the ticket

```php
$ticket_id = 1;
$filename = 'example.xml';
$description = 'demo xml';
$file_path = '/tmp/example.xml';
$tracClient->uploadAttachment($ticket_id, $filename, $description, $file_path)
```

### downloadAttachment($ticket_id, $filename, $save_path)

download the attachment from the ticket

```php
$ticket_id = 1;
$filename = 'example.xml';
$save_path = '/tmp/example.xml';
$tracClient->downloadAttachment($ticket_id, $filename, $save_path)
```

### deleteAttachment($ticket_id, $filename)

delete the attachment from ticket

```php
$ticket_id = 1;
$filename = 'example.xml';
$tracClient->deleteAttachment($ticket_id, $filename)
```