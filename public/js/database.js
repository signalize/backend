import {MD5} from "./md5.js";

export class Database {
    static store(collection, data) {
        data = JSON.stringify(data);
        localStorage.setItem(collection + "_" + MD5(data), data);
    }

    static collection(collection) {
        let archive = new Collection();
        Object.keys(localStorage).forEach((k) => {
            if (k.substring(0, collection.length) === collection) {
                archive.push(JSON.parse(localStorage.getItem(k)));
            }
        });
        return archive;
    }

}

export class Collection extends Array {
    order(key, order) {
        order = order.toUpperCase();
        return this.sort((a, b) => {
            if (a[key] < b[key]) {
                return order === 'ASC' ? -1 : 1;
            }
            if (a[key] > b[key]) {
                return order === 'ASC' ? 1 : -1;
            }
        });
    }
}