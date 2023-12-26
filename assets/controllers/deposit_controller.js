import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['amount', 'loader']
    connect() {
        this.loaderTarget.hidden = true;
    }

    async sendDeposit() {

        this.loaderTarget.hidden = false;
        fetch('/panel/user/deposit-send', {
            method: "POST",
            mode: "same-origin",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                'amount' : this.amountTarget.value,
            })
        }).then(
            async (response) => {
                if(response.ok) {
                    const json = await response.json();
                    this.loaderTarget.hidden = true;
                    window.location.replace(window.location.origin + '/panel/user/contracts');
                }
            }
        ).catch(
            (e) => { 
                this.loaderTarget.hidden = true;
                console.log(e) 
            }
        ) 

    }

}