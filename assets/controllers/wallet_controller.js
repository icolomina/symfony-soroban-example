import { Controller } from '@hotwired/stimulus';
import { isConnected, getPublicKey } from "@stellar/freighter-api";

export default class extends Controller {

    static targets = ['loader']
    connect() {
        this.loaderTarget.hidden = true;
    }

    async login() {
        if (!await isConnected()) {
            alert('User has not Freighter. Install Freighter and retry');
        }

        let publicKey = '';
        this.loaderTarget.hidden = false;
        getPublicKey().then(
            (pkey) => {
                publicKey = pkey;
                fetch('/login', {
                    method: "POST",
                    mode: "same-origin",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ 'address' : publicKey })

                })
                .then(
                    async (response) => {
                        this.loaderTarget.hidden = true;
                        if(response.ok) {
                            const json = await response.json();
                            window.location.replace(json.url);
                        }
                    }
                    
                )
            },
            (e) => {
                this.loaderTarget.hidden = true;
                console.log(e);
            }
        )
    }
}
