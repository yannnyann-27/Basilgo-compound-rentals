<?php
// App-wide settings. No database credentials live here (this build is JSON-file backed).
const APP_NAME    = 'Basilgo Compound – House and Room Rentals'; // full title (browser tab, invoices, footer)
const APP_SHORT   = 'Basilgo Compound';
const APP_TAGLINE = 'House and Room Rentals';
const PAY_METHOD  = 'Cash';   // the compound accepts cash only: no GCash, bank or other online payments
const ELEC_RATE   = 16.00;    // PHP per kWh (default; admin can change under Rates)
const WATER_RATE  = 35.00;    // PHP per m3, estimate for Cebu City - verify with MCWD
