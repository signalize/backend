import {Socket} from "./socket.js";

const socket = new Socket();

export class Widget extends HTMLElement {
    static service = null;

    constructor() {
        super();
        const self = this;
        const widget = this.constructor;

        let cache = null;
        try {
            cache = JSON.parse(localStorage.getItem(widget.service));
        } catch (e) {
        }
        self.innerHTML = widget.render(cache);

        if (widget.service) {
            socket.addEventListener('message', function (event) {
                let message = Widget.decode(event.data);
                if (message.service === widget.service) {
                    localStorage.setItem(message.service, JSON.stringify(message.package));
                    self.innerHTML = widget.render(message.package);
                }

            });
        }
    }

    static decode(msg) {
        let seperator = (msg.indexOf("\r\n\r\n") > -1) ? "\r\n\r\n" : "\n\n";
        if (msg.indexOf(seperator) > -1 && msg.indexOf(seperator) < 32) {
            let service = msg.substring(1, msg.indexOf(seperator)).trim();
            let rawPackage = msg.substring(msg.indexOf(seperator), msg.length);
            let pkg = JSON.parse(rawPackage);
            if (pkg) {
                return {
                    service: service,
                    package: pkg
                };
            }
        }
    }


    static parse(strings, ...values) {
        let str = "";
        strings.forEach((string, i) => {
            str += string;
            if (values[i]) {
                str += values[i];
            }
        });
        return str;
    }
}