'use strict'
import React from 'react'
import PropTypes from 'prop-types'
import Base from '../DeviceForms/Base.jsx'

export default class Printer extends Base {
  constructor(props) {
    super(props)
    this.state = {}
  }

  render() {
    return (
      <div></div>
    )
  }
}

Printer.propTypes = {
  device: PropTypes.object.isRequired,
  update: PropTypes.func.isRequired
}
