import React, { FC, Fragment, ReactNode } from 'react';
import classnames from 'classnames';
import moment from 'moment';
import { Transaction } from 'types/etherscan';
import { useAppSelector } from 'hooks';
import { Tooltip } from 'flowbite-react';

interface Props {
    children?: ReactNode;
    transactions?: Transaction[];
}

const Row: FC<{ transaction: Transaction }> = ({
    transaction: { name, direction, quantity, from, to, fee, timestamp }
}) => {
    const trimAddress = (address: string) => {
        return `${address.substring(0, 4)}...${address.substring(
            address.length - 4,
            address.length
        )}`;
    };

    return (
        <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <th
                scope="row"
                className="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
            >
                {name}
            </th>
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
            <td className="px-6 py-4">{quantity}</td>
            {/* From */}
            <td className="px-6 py-4">
                {from ? (
                    <Tooltip content={from}>
                        <span>{trimAddress(from)}</span>
                    </Tooltip>
                ) : (
                    <span className="block font-bold text-center" />
                )}
            </td>

            {/* To */}
            <td className="px-6 py-4">
                {to ? (
                    <Tooltip content={to}>
                        <span>{trimAddress(to)}</span>
                    </Tooltip>
                ) : (
                    <span className="block font-bold text-center" />
                )}
            </td>

            <td className="px-6 py-4">{fee}</td>
            <td
                className="px-6 py-4"
                title={moment.unix(timestamp).format('Do MMM YYYY \\a\\t HH:mm:ss')}
            >
                {moment.unix(timestamp).fromNow()}
            </td>
        </tr>
    );
};

const Table: FC<Props> = ({ children, transactions }) => {
    const columns: string[] = ['Item', 'Direction', 'Quantity', 'From', 'To', 'Txn Fee', 'Time'];
    const status = useAppSelector((state) => state.etherscan.status);

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
                        {transactions?.map((transaction, index) => {
                            return (
                                <Row
                                    transaction={transaction}
                                    key={`${transaction.hash}-${index}`}
                                />
                            );
                        })}
                        {!transactions?.length && (
                            <tr className="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td
                                    className={classnames(
                                        'px-6 py-4 text-sm text-center text-muted',
                                        {
                                            'text-muted': status !== 'error',
                                            'text-red-600 font-medium': status === 'error'
                                        }
                                    )}
                                    colSpan={7}
                                >
                                    {status === 'loading' && 'The transactions are being loaded...'}
                                    {status === 'success' && 'No transactions were found :('}
                                    {status === 'idle' && 'Ready to fetch transactions!'}
                                    {status === 'error' &&
                                        'An error occurred while retrieving transactions!'}
                                </td>
                            </tr>
                        )}
                        {children}
                    </tbody>
                </table>
            </div>
            {transactions?.length ? (
                <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-500">
                        Showing {transactions.length} transactions
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
