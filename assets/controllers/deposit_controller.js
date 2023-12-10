import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['amount']
    connect() {}

    async sendDeposit() {

        await fetch('/panel/user/deposit-send', {
            method: "POST",
            mode: "same-origin",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                'amount' : this.amountTarget.value,
            })
        });

    }

}