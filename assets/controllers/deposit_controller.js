import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {}

    async sendDeposit() {

        await fetch('/panel/user/deposit-send', {
            method: "POST",
            mode: "same-origin",
            headers: {
                "Content-Type": "application/json",
            }
        });

    }

}