import React, { FC } from 'react';
import Filters from './Filters';
import Table from './Table';

const App: FC = () => {
    return (
        <div className="mt-10 space-y-4 ">
            <Filters />
            <Table />
        </div>
    );
};

export default App;
