import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = [ "receiver", "label", "description", "token" ]
    connect() {}

    async createContract() {

        await fetch('/panel/user/contract', {
            method: "POST",
            mode: "same-origin",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                'receiver' : this.receiverTarget.value,
                'label' : this.labelTarget.value,
                'description' : this.descriptionTarget.value,
                'token' : this.tokenTarget.value
            })

        });

        window.location.replace('http://127.0.0.1:8000/panel/user/contracts');
    }
}