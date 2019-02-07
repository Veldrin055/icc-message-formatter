import React, { Component } from 'react';
import moment from 'moment';

const timezone = 'Australia/Melbourne';

class Clock extends Component {
  
  state = {
      time: moment().tz(timezone),
  };
  
  componentDidMount() {
    this.intervalID = setInterval(() => this.tick(), 1000);
  }
  componentWillUnmount() {
    clearInterval(this.intervalID);
  }

  tick() {
    this.setState({
      time: moment().tz(timezone),
    });
  }
  render() {
    const { time } = this.state;
    const { updating } = this.props;
    const className = 'clock' + (updating ? ' update' : '');
    return (
      <div className={className}>
        <span className="SUF" style={{ fontWeight: 'bold' }}>
          {time.format('HH:mm:ss')}
        </span>
      </div>
    );
  }
}

export default Clock;
