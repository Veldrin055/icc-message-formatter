import React from 'react';

const PagerEvent = ({ event }) => {
  const {
    dateTime,
    eventId,
    responseRequired,
    brigades = [],
    msg,
    updates = [],
  } = event;
  return (
    <div
      id="ev_"
      className="M_Log"
      style={{
        fontWeight: 'bold',
      }}
    >
      <div id="ev_head">
        <span className="SUF">
          Start :{dateTime && dateTime.format('HH:mm:ss')} -{' '}
          <span style={{ backgroundColor: '#f00' }}>{eventId}</span>-{' '}
          {agency(responseRequired)}
        </span>
      </div>
      <div id="ev_body">
        <span className="E_M">{msg + ' '}</span>
        <span className="UNIT_0">{brigades.join(' ')}</span>
      </div>
    </div>
  );
};

const agency = responseRequired => {
  const agencies = {
    A: 'AV',
    F: 'FIRE',
    P: 'POL',
    R: 'RESC',
    S: 'SES',
  };
  return (
    responseRequired &&
    responseRequired
      .split('')
      .map(r => agencies[r])
      .join(' / ')
  );
};

export default PagerEvent;
