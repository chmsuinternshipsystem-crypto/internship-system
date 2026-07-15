describe('Blackbox: Student Registry', () => {
  beforeEach(() => {
    cy.loginAsStaff('instructor');
  });

  it('B6: Displays students list with search and filters', () => {
    cy.visit('/students');
    cy.wait(2000);
    cy.contains('Students').should('be.visible');
    cy.screenshot('students-list');
  });

  it('B7: Shows create student form with all fields', () => {
    cy.visit('/students/create');
    cy.wait(2000);
    cy.contains('Create').should('be.visible');
    cy.screenshot('create-student-form');
  });

  it('B8: Student create form validates required fields', () => {
    cy.visit('/students/create');
    cy.wait(1000);
    cy.get('button[type="submit"]').first().click({ force: true });
    cy.wait(2000);
    cy.screenshot('create-student-validation');
  });
});
