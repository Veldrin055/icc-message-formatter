import React from 'react';

const Paging = ({ onChange, value, values }) => {
    return (
      <div className="paging">
        <b>PAGING: </b>
        <select name="recstoshow" onChange={onChange} defaultValue={value}>
            {values.map(v => <option key={v} value={v}>{v}</option>)}
        </select>
      </div>
    );
  };

  export default Paging;