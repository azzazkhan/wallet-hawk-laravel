import ReactDOM from 'react-dom/client';
import App from 'components/App';
import store from 'store';
import { Provider } from 'react-redux';

const container = document.getElementById('root');

// eslint-disable-next-line no-unused-expressions, @typescript-eslint/no-unused-expressions
container &&
    ReactDOM.createRoot(container).render(
        <Provider store={store}>
            <App />
        </Provider>
    );
