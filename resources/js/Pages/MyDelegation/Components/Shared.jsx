import React from 'react';
import { CLS } from './constants';

export const Divider   = () => <div className={CLS.divider} />;
export const FormField = ({ label, children }) => <div><label className={CLS.label}>{label}</label>{children}</div>;
export const TextInput = ({ value, onChange, placeholder, ...p }) => (
    <input type="text" value={value} onChange={onChange} placeholder={placeholder} className={CLS.input} {...p} />
);
