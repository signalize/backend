export class Widget extends HTMLElement {
    constructor() {
        super();
        this.innerHTML = this.render();
    }

    render() {
        return '';
    }
}