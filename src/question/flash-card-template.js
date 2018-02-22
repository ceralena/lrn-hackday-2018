LearnosityAmd.define(['underscore'], function (_) {
    return _.template(`
        <div class="card-container">
          <div class="card">
            <div class="front"><%= front %></div>
            <div class="back"><%= back %></div>
          </div>
        </div>
    `);
});