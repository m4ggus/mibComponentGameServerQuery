# PHP: mib/gameserverquery

## Library for querying game servers for getting game status

### Supported Game Types

Currently there are two supported game types used by the adapter class:

- quake3 based game server
- source based game server

### Description

This Library provides and adapter for querying various game server types for getting status information like player
information, map information and lot more depending on the game api. The library can be extended by providing modules
for specific games.

### Usage

    // create a socket for the adapter
    $socket  = new Mib\Component\Network\UDP\Socket();
    
    // create the adapter and pass the created socket
    $adapter = new Mib\Component\GamerServerQuery\ServerQueryAdapter();
    
    // query a source engine game server
    $sourceGameInformation = $adapter->getStatus('source', '127.0.0.1:27015');
    
    // query a quake3-like engine game server
    $quake3GameInformation = $adapter->getStatus('quake3', '127.0.0.1:27960');
