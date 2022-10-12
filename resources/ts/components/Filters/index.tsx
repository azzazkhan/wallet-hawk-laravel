import React, { FC } from 'react';
import DesktopFilters from './DesktopFilters';

const Filters: FC = () => {
    return (
        <div className="flex flex-col mt-10 space-y-4 select-none">
            <DesktopFilters />
        </div>
    );
};

export default Filters;
