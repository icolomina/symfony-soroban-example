import { Controller } from '@hotwired/stimulus';
import { SorobanRpc, Contract, Address, BASE_FEE, Networks, TransactionBuilder, Transaction, nativeToScVal, xdr } from 'stellar-sdk';
import { getPublicKey, signTransaction } from "@stellar/freighter-api";


export default class extends Controller {

    static targets = [ "amount" ];
    static values  = { "contract" : String }; 

    connect() {}

    async sendDeposit() {

        const address = await getPublicKey();
        const server = new SorobanRpc.Server('https://soroban-testnet.stellar.org:443');
        const contract = new Contract(this.contractValue);
        const op = contract.call('deposit', Address.fromString(address).toScVal(), nativeToScVal(this.amountTarget.value, { type: "i128" }) );

        const account = await server.getAccount(address);
        const tx = new TransactionBuilder(account, { fee: BASE_FEE })
            .addOperation(op)
            .setNetworkPassphrase(Networks.TESTNET)
            .setTimeout(30)
            .build()
        ;
        
        let sim = await server.simulateTransaction(tx);
        const readyTx = SorobanRpc.assembleTransaction(tx, sim);
        signTransaction(readyTx.build().toXDR()).then(
            async (signedTs) => {
                const result = await server.sendTransaction(new Transaction(signedTs, Networks.TESTNET));
                console.log(result);
            }
        )

    }

}