export class Socket extends WebSocket {
    constructor() {
        let socketAddress = 'ws://' + window.location.host + ':' + window.location.port + '/sock/';
        socketAddress += '?SID=0FB1CF9AAB460F7834CB248B4DDFA1FDC9A804C7.1550220392';
        super(socketAddress);
    }

}