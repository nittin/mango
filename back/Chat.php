<?php
namespace ChatApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $host= gethostname();
        $ip = gethostbyname($host);
        echo "start listener: ".$host." ip: ".$ip."\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        //store the new connection
        $this->clients->attach($conn);

        echo "someone connected\n";
    }
    public function onMessage(ConnectionInterface $from, $msg) {
        //send the message to all the other clients except the one who sent.
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "someone has disconnected";
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "an error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}