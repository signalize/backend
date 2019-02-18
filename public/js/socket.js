export class Socket {
    socket;

    constructor() {
        this.connect();
    }

    connect() {
        let socketAddress = 'ws://' + window.location.host + ':' + window.location.port + '/sock/';
        socketAddress += '?SID=0FB1CF9AAB460F7834CB248B4DDFA1FDC9A804C7.1550220392';
        this.socket = new WebSocket(socketAddress);
    }


    send(service, pkg) {
        if (this.socket.readyState === this.socket.CLOSING || this.socket.readyState === this.socket.CLOSED) {
            console.log('Not able to connect to the socket. Retry in 5 seconds...');
            this.connect();
            return setTimeout(() => {
                this.send(service, pkg);
            }, 5000);
        }
        if (this.socket.readyState === this.socket.CONNECTING) {
            return setTimeout(() => {
                this.send(service, pkg);
            }, 100);
        }
        this.socket.send('/' + service.trim() + "\r\n\r\n" + JSON.stringify(pkg));
    }

    addEventListener(type, listener) {
        return this.socket.addEventListener(type, listener);
    }
}