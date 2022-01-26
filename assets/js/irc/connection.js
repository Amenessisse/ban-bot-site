import * as tmi from 'tmi.js';

function onConnectedHandler(address, port) {
  console.log(` * Connected to ${address}:${port}`);
}

export default function connection(target) {
  // Define configuration options

  const opts = {
    identity: {
      username: target.dataset.username,
      password: `oauth:${target.dataset.token}`,
    },
    channels: [
      target.dataset.username,
    ],
    options: {
      debug: true,
      messagesLogLevel: 'info',
      skipUpdatingEmotesets: true,
      skipMembership: true,
    },
    connection: {
      reconnect: true,
      secure: true,
    },
  };

  // Create a client with our options
  const client = new tmi.client(opts);

  // Register our event handlers (defined below)
  client.on('connected', onConnectedHandler);

  // Connect to Twitch:
  client.connect().catch(console.error);
}
