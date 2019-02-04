import React from 'react';
import PagerEvent from './PagerEvent';
import Clock from './Clock';

const MainPager = ({ events }) => (
  <div className="main_Pager">
    <TimeHeader />
    <Clock />
    <Clear />
    {events.map(e => (
      <PagerEvent key={e.dateTime.valueOf()} event={e} />
    ))}
  </div>
);

const TimeHeader = () => (
  <div
    style={{
      textAlign: 'left',
      float: 'left',
      fontSize: '7px',
      fontWeight: 'bold',
    }}
  >
    <span
      style={{
        background: '#0C0',
        width: '200px',
        paddingRight: '5px',
        paddingLeft: '5px',
      }}
    >
      0 &gt; 4:59min
    </span>
    |
    <span
      style={{
        background: '#FF0',
        color: '#000',
        width: '200px',
        paddingRight: '5px',
        paddingLeft: '5px',
      }}
    >
      5:00min &gt; 14:59min
    </span>
    |
    <span
      style={{
        background: '#F00',
        width: '200px',
        paddingRight: '5px',
        paddingLeft: '5px',
      }}
    >
      &gt; 15:00min
    </span>
  </div>
);

const Clear = () => (<div style={{clear: 'both'}}></div>);

export default MainPager;
