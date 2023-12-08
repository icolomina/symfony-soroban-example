import { startStimulusApp } from '@symfony/stimulus-bridge';
import wallet_controller from './controllers/wallet_controller';
import contract_controller from './controllers/contract_controller';

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));
// register any custom, 3rd party controllers here
app.register('wallet', wallet_controller);
app.register('contract', contract_controller);
