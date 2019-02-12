import React from 'react';
import PagerEvent from './PagerEvent';
import Clock from './Clock';

const MainPager = ({ events, error, updating }) => (
  <div className="main_Pager">
    <TimeHeader {...{ error }}/>
    <Clock {...{updating}}/>
    <Clear />
    {events.map((e, i) => (
      <PagerEvent key={e.dateTime.valueOf()} event={e} stripe={i % 2 == 0}/>
    ))}
  </div>
);

const TimeHeader = ({ error }) => (
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
    {error && <span style={{ fontSize: '24px', color: 'red' }}><span role='img' ariaLabel='Error'>❌</span> Problem updating file</span>}
  </div>
);

const Clear = () => (<div style={{clear: 'both'}}></div>);

export default MainPager;
