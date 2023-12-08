import { Controller } from '@hotwired/stimulus';
import { isConnected, getPublicKey } from "@stellar/freighter-api";

export default class extends Controller {

    connect() {}

    async connectWallet() {
        if (!await isConnected()) {
            alert('User has not Freighter. Install Freighter ans retry');
        }

        let publicKey = '';
        getPublicKey().then(
            async (pkey) => {
                publicKey = pkey;
                await fetch('/panel/user/address', {
                    method: "POST",
                    mode: "same-origin",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ 'address' : publicKey })

                });

                window.location.reload();
            },
            (e) => {
                console.log(e);
            }
        )
    }
}
