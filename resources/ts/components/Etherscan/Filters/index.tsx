import React, { FC } from 'react';
import DesktopFilters from './DesktopFilters';

const Filters: FC = () => {
    return (
        <div className="flex flex-col select-none">
            <DesktopFilters />
        </div>
    );
};

export default Filters;
