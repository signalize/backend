import {Socket} from "./socket.js";
import {Database} from "./database.js";

const socket = new Socket();

export class Widget extends HTMLElement {

    constructor() {
        super();
        const self = this;
        const service = this.constructor.service;

        self.style.display = 'inline-block';
        self.style.width = '100%';
        self.style.height = '100%';

        if(self.initiate){
            self.initiate();
        }

        if (service) {
            if(self.update){
                self.update(Database.collection(service));
            }
            socket.send(service, {subscribe: true});
            socket.addEventListener('message', function (event) {
                self.decode(event.data).then(message => {
                    if (message.service === service) {
                        if(message.package.hasOwnProperty('0')){
                            Object.keys(message.package).forEach(function(key) {
                                Database.store(message.service, message.package[key]);
                            });
                        } else {
                            Database.store(message.service, message.package);
                        }

                        if(self.update){
                            self.update(Database.collection(service));
                        }
                    }
                });
            });
        }
    }

    decode(msg) {
        return new Promise(resolve => {
            let seperator = (msg.indexOf("\r\n\r\n") > -1) ? "\r\n\r\n" : "\n\n";
            if (msg.indexOf(seperator) > -1 && msg.indexOf(seperator) < 32) {
                let service = msg.substring(1, msg.indexOf(seperator)).trim();
                let rawPackage = msg.substring(msg.indexOf(seperator), msg.length);
                let pkg = JSON.parse(rawPackage);
                if (pkg) {
                    resolve({
                        service: service,
                        package: pkg
                    });
                }
            }
        })
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