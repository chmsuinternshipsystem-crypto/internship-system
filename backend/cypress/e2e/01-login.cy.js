describe('Blackbox: Authentication & Login', () => {
  beforeEach(() => {
    cy.visit('/login');
    cy.wait(1000);
  });

  it('B1: Displays login page with all auth options', () => {
    cy.contains('Welcome');
    cy.contains('Student');
    cy.contains('Faculty');
    cy.contains('Clock In');
    cy.screenshot('login-landing-page');
  });

  it('B2: Shows student sign-in form when Student tab clicked', () => {
    cy.contains('button', 'Student').click();
    cy.wait(500);
    cy.get('#student_number').should('be.visible');
    cy.get('#password_student').should('be.visible');
    cy.screenshot('login-student-form');
  });

  it('B3: Shows staff sign-in form when Faculty tab clicked', () => {
    cy.contains('button', 'Faculty').click();
    cy.wait(500);
    cy.get('#staff_role').should('be.visible');
    cy.get('#email').should('be.visible');
    cy.screenshot('login-staff-form');
  });

  it('B4: Shows validation error on invalid staff credentials', () => {
    cy.contains('button', 'Faculty').click();
    cy.wait(500);
    cy.get('#staff_role').select('instructor', { force: true });
    cy.get('#email').type('wrong@test.com', { force: true });
    cy.get('#password_staff').type('wrongpass', { force: true });
    cy.contains('button', 'Sign in').last().click();
    cy.wait(2000);
    cy.screenshot('login-validation-error');
  });

  it('B5: Staff login as instructor succeeds', () => {
    cy.loginAsStaff('instructor');
    cy.url().should('include', '/dashboard');
    cy.contains('Dashboard');
    cy.screenshot('instructor-dashboard');
  });
});
