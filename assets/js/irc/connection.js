import 'tmi.js';

function onConnectedHandler(address, port)
{
    console.log(` * Connected to ${address}:${port}`);
}

export function connection(target)
{
    // Define configuration options
    let opts;
    opts = {
        identity: {
            username: target.dataset.username,
            password: 'oauth:' + target.dataset.token,
        },
        channels: [
            target.dataset.username,
        ],
        options: {debug: true, messagesLogLevel: "info"},
        connection: {
            reconnect: false,
            secure: true
        },
    };

    // Create a client with our options
    const client = new tmi.client(opts);

    // Register our event handlers (defined below)
    client.on('connected', onConnectedHandler);

    // Connect to Twitch:
    client.connect().catch(console.error);
}