import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = [ "label", "description", "token", "loaderButton", "submitButton" ]
    connect() {
        this.loaderButtonTarget.hidden = true;
        this.submitButtonTarget.hidden = false;
    }

    async createContract() {

        this.loaderButtonTarget.hidden = false;
        this.submitButtonTarget.hidden = true;

        fetch('/contract-create', {
            method: "POST",
            mode: "same-origin",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                'label' : this.labelTarget.value,
                'description' : this.descriptionTarget.value,
                'token' : this.tokenTarget.value
            })

        }).then(
            async (response) => {
                if(response.ok) {
                    const json = await response.json();
                    this.loaderButtonTarget.hidden = true;
                    window.location.replace(window.location.origin);
                }
            }
        ).catch(
            (e) => { 
                this.loaderButtonTarget.hidden = true;
                console.log(e) 
            }
        )        
    }
}