Cypress.Commands.add('loginAsStaff', (role = 'instructor') => {
  cy.visit('/login');
  cy.wait(1000);
  cy.contains('button', 'Faculty').click();
  cy.wait(500);
  cy.get('#staff_role').select(role, { force: true });
  cy.get('#email').type(`${role}@chmsu.edu.ph`, { force: true });
  cy.get('#password_staff').type('password', { force: true });
  cy.contains('button', 'Sign in').last().click();
  cy.wait(2000);
  cy.url().should('not.include', '/login');
});

Cypress.Commands.add('loginAsStudent', () => {
  cy.visit('/login');
  cy.wait(1000);
  cy.contains('button', 'Student').click();
  cy.wait(500);
  cy.get('#student_number').type('20230001', { force: true });
  cy.get('#password_student').type('20230001', { force: true });
  cy.contains('button', 'Sign in').first().click();
  cy.wait(2000);
});
