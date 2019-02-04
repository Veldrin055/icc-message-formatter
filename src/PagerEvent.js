import React from 'react';
import moment from 'moment';

const PagerEvent = ({ event }) => {
  const {
    startTime,
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
          Start :{startTime && startTime.format('HH:mm:ss')} -{' '}
          <span style={{ backgroundColor: '#f00' }}>{eventId}</span>-{' '}
          {agency(responseRequired)}
        </span>
      </div>
      <div id="ev_body">
        <span className="E_M">{msg + ' '}</span>
        {brigades.map(b => <Unit key={b.code} {...{...b}}/>)}
        {updates.length > 0 ? 
          <FurtherInformation {...{ updates }} />  
        : null}
      </div>
    </div>
  );
};

const agency = responseRequired => {
  const agencies = {
    A: 'AV 🚑',
    F: 'FIRE 🚒',
    P: 'POL 🚓',
    R: 'RESC ⛑️',
    S: 'SES ⚒️',
  };
  return (
    responseRequired &&
    responseRequired
      .split('')
      .map(r => agencies[r])
      .join(' / ')
  );
};

const Unit = ({ code, dateTime, cancelled }) => {
  const classes = [];
  const now = moment();
  if (cancelled) {
    classes.push('UNIT_CANCEL');
  }
  if(now.subtract(5, 'minutes').isBefore(dateTime)) {
    classes.push('UNIT_0');
  } else if (now.subtract(15, 'minutes').isBefore(dateTime)) {
    classes.push('UNIT_5');
  } else {
    classes.push('UNIT_15');
  }
  return (<span className={classes.join(' ')}>{code} </span>)
}

const FurtherInformation = ({ updates }) => {
  const update = updates.sort((a, b) => {
    if (a.dateTime.isBefore(b.dateTime)) { return -1}
    else if (a.dateTime.isAfter(b.dateTime)) { return 1}
    else return 0;
  })[0];
  return(
    <React.Fragment>
      <br /><span className="F_B">Further {update.dateTime.format('HH:mm:ss')}</span><span className="E_M"> - {update.msg} </span>
    </React.Fragment>
  );
};

export default PagerEvent;
