import ReactDOM from 'react-dom/client';
import Etherscan from 'components/Etherscan';
import Opensea from 'components/Opensea';
import store from 'store';
import { Provider } from 'react-redux';
import { createPortal } from 'react-dom';
import { FC } from 'react';

const EtherscanApp: FC = () => {
    const container = document.getElementById('etherscan_module');

    if (!container) return null;

    return createPortal(<Etherscan />, container);
};

const OpenseaApp: FC = () => {
    const container = document.getElementById('opensea_module');

    if (!container) return null;

    return createPortal(<Opensea />, container);
};

const container = document.getElementById('root');

// eslint-disable-next-line no-unused-expressions, @typescript-eslint/no-unused-expressions
container &&
    ReactDOM.createRoot(container).render(
        <Provider store={store}>
            <EtherscanApp />
            <OpenseaApp />
        </Provider>
    );
