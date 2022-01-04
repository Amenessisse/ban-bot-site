import {connection} from "./connection";

const buttonBotConnection = document.getElementById('button-bot-connect');

export function initializeButtonBotConnect()
{
    if (buttonBotConnection !== null) {
        buttonBotConnection.addEventListener('click', (event) => {
            connection(event.target);
        });
    }
}
