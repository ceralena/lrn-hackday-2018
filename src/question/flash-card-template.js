LearnosityAmd.define(['underscore'], function (_) {
    return _.template(`
        <div class="card-container">
          <div class="card">
            <div class="front face">
                <div class="face-content">
                    <%= front %>
                    <br>
                    <input type="text" class="response">
                </div>
            </div>
            <div class="back face">
                <div class="face-content">
                    <%= back %>
                </div>
            </div>
          </div>
        </div>
    `);
});