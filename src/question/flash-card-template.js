LearnosityAmd.define(['underscore'], function (_) {

    const header = `
        <div class="card-header">
            <span>image</span>
            <span>Japanese</span>
            <span class="cards-left"></span>
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

    const backBody = `
        <div class="card-body">
            <%= back %>
        </div>
    `;

    const backFooter = `
        <div class="card-footer">
            <span class="validation-icon"></span>
            <span class="validation-message"></span>
            <a href="#" class="button next">Next</a>
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
                    ${backBody}
                    ${backFooter}
                </div>
            </div>
          </div>
        </div>
    `);
});
