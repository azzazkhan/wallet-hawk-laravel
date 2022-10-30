import React, { FC, Fragment, ReactNode, useMemo } from 'react';
import classnames from 'classnames';
import moment from 'moment';
import { useAppSelector } from 'hooks';
import { Event } from 'types/opensea';

interface Props {
    children?: ReactNode;
    events?: Event[];
}

const Row: FC<{ event: Event }> = ({
    event: { image, name, direction, from, to, schema, event_type, value, timestamp, event_id }
}) => {
    const params = useMemo(() => new URLSearchParams(window.location.search), []);
    const trimAddress = (address: string) => {
        return `${address.substring(0, 4)}...${address.substring(
            address.length - 4,
            address.length
        )}`;
    };

    return (
        <tr className="bg-white border-b hover:bg-gray-50">
            {/* Name + Image */}
            <th scope="row" className="px-6 py-4 font-medium text-gray-900">
                <div className="flex items-center space-x-3">
                    {name || image ? (
                        <Fragment>
                            {image ? (
                                <img
                                    src={image}
                                    className="flex-shrink-0 w-10 h-10 rounded-lg"
                                    alt={name || 'Unavailable'}
                                />
                            ) : (
                                <div className="flex-shrink-0 block w-10 h-10 rounded-lg" />
                            )}
                            {name ? (
                                <span className="line-clamp-1">{name}</span>
                            ) : (
                                <span className="block font-bold text-center">--</span>
                            )}
                        </Fragment>
                    ) : null}
                </div>
            </th>

            {/* Direction */}
            <td
                className={classnames('px-6 py-4 flex items-center space-x-1', {
                    'text-red-600': direction === 'out',
                    'text-green-600': direction === 'in'
                })}
            >
                {direction === 'out' && <i className="fas fa-arrow-up" aria-hidden="true" />}
                {direction === 'in' && <i className="fas fa-arrow-down" aria-hidden="true" />}
                {direction ? (
                    <span className="uppercase">{direction}</span>
                ) : (
                    <span className="block font-bold text-center">--</span>
                )}
            </td>
            {/* <td className="px-6 py-4">{quantity}</td> */}

            {/* From */}
            <td className="px-6 py-4" title={from?.address || ''}>
                {from?.address ? (
                    trimAddress(from.address)
                ) : (
                    <span className="block font-bold text-center" />
                )}
            </td>

            {/* To */}
            <td className="px-6 py-4" title={to?.address || ''}>
                {to?.address ? (
                    trimAddress(to.address)
                ) : (
                    <span className="block font-bold text-center" />
                )}
            </td>

            {/* Schema */}
            <td className="px-6 py-4 uppercase">{schema}</td>

            {/* Event Type */}
            <td className="px-6 py-4 capitalize">{event_type}</td>

            {/* Value */}
            <td className="px-6 py-4">
                {value ? (
                    <span className="whitespace-nowrap">{value} ETH</span>
                ) : (
                    <span className="block font-bold text-center">--</span>
                )}
            </td>

            {/* Timestamp */}
            <td
                className="px-6 py-4"
                title={moment(timestamp).format('Do MMM YYYY \\a\\t HH:mm:ss')}
            >
                {moment(timestamp).fromNow()}
            </td>

            <td className="px-6 py-4">
                <a
                    href={`transactions/${params.get('address')}/${event_id}`}
                    className="inline-flex items-center h-8 px-3 text-xs font-medium text-blue-500 transition-colors border border-blue-500 rounded-md whitespace-nowrap hover:text-white hover:bg-blue-500"
                >
                    Details
                </a>
            </td>
        </tr>
    );
};

const Table: FC<Props> = ({ children, events }) => {
    const columns: Nullable<string>[] = [
        'Item',
        'Direction',
        'From',
        'To',
        'Schema',
        'Event Type',
        'Value',
        'Occurred',
        null
    ];
    const status = useAppSelector((state) => state.opensea.status);

    return (
        <Fragment>
            <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table className="w-full text-sm text-left text-gray-500">
                    <thead className="text-gray-700 uppercase bg-gray-50">
                        <tr>
                            {columns.map((label, index) => {
                                return (
                                    <th scope="col" className="px-6 py-3" key={index}>
                                        {label}
                                    </th>
                                );
                            })}
                        </tr>
                    </thead>
                    <tbody>
                        {events?.map((event) => {
                            return <Row event={event} key={event.event_id} />;
                        })}
                        {!events?.length && (
                            <tr className="bg-white border-b hover:bg-gray-50 ">
                                <td
                                    className={classnames(
                                        'px-6 py-4 text-sm text-center text-muted',
                                        {
                                            'text-muted': status !== 'error',
                                            'text-red-600 font-medium': status === 'error'
                                        }
                                    )}
                                    colSpan={9}
                                >
                                    {status === 'loading' && 'The events are being loaded...'}
                                    {status === 'success' && 'No events were found :('}
                                    {status === 'idle' && 'Ready to fetch events!'}
                                    {status === 'error' &&
                                        'An error occurred while retrieving events!'}
                                </td>
                            </tr>
                        )}
                        {children}
                    </tbody>
                </table>
            </div>
            {events?.length ? (
                <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-500">
                        Showing {events.length} transactions
                    </span>

                    <span className="px-3 py-1.5 rounded hover:bg-gray-300 transition-colors font-medium text-sm">
                        Download CSV
                    </span>
                </div>
            ) : null}
        </Fragment>
    );
};

export default Table;
