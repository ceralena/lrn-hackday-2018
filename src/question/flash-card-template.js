LearnosityAmd.define(['underscore'], function (_) {

    const header = `
        <div class="card-header">
            <span>image</span>
            <span>Japanese</span>
            <span>Cards left: 19</span>
        </div>
    `;

    const body = `
        <div class="card-body">
            <%= front %>
            <br>
            <input type="text" class="response">
        </div>
    `;
    
    const frontFooter = `
        <div class="card-footer">
            <a href="#" class="button skip">Skip</a>
            <a href="#" class="button check">Check</a>
        </div>
    `;

    return _.template(`
        <div class="card-container">
          <div class="card">
            <div class="front face">
                <div class="face-content">
                    ${header}
                    ${body}
                    ${frontFooter}
                </div>
            </div>
            <div class="back face">
                <div class="face-content">
                    ${header}
                    <%= back %>
                </div>
            </div>
          </div>
        </div>
    `);
});
