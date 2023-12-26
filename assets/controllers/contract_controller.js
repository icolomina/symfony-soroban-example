import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = [ "receiver", "label", "description", "token", "loader" ]
    connect() {
        this.loaderTarget.hidden = true;
    }

    async createContract() {

        this.loaderTarget.hidden = false;
        fetch('/panel/user/contract', {
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