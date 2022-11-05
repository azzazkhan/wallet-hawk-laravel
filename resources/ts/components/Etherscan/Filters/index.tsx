import React, { FC } from 'react';
import DesktopFilters from './DesktopFilters';
import MobileFilters from './MobileFilters';

const Filters: FC = () => {
    return (
        <div className="flex flex-col select-none">
            <DesktopFilters />
            <MobileFilters />
        </div>
    );
};

export default Filters;
