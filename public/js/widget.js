export class Widget extends HTMLElement {
    channel = null;

    constructor() {
        super();
        const widget = this;

        if (this.channel) {
            let socketAddress = 'ws://' + window.location.host + ':' + window.location.port + '/sock/';
            socketAddress += '?SID=0FB1CF9AAB460F7834CB248B4DDFA1FDC9A804C7.1550220392';

            const socket = new WebSocket(socketAddress);
            socket.addEventListener('message', function (event) {
                console.log(event.data);
                widget.innerHTML = widget.render(event.data);
            });
        }
    }

    render(data) {
        return '';
    }
}