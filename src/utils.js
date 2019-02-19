import alerts from './assets/alerts.json';

const isSpecialAlert = units => units && units.length && units.some(r => alerts.includes(r.code));

export {
    isSpecialAlert
};