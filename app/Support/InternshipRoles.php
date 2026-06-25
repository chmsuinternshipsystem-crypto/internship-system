<?php

namespace App\Support;

/**
 * Central source of truth for role names and grouped permissions.
 */
final class InternshipRoles
{
    public const STUDENT_PORTAL = 'student';

    public const INSTRUCTOR = 'instructor';

    public const CHAIRPERSON = 'chairperson';

    public const DEAN = 'dean';

    /** Roles stored in users.role that can sign in as staff. */
    public static function staffEmailRoles(): array
    {
        return [
            self::INSTRUCTOR,
            self::CHAIRPERSON,
            self::DEAN,
        ];
    }

    /** Roles allowed to open staff dashboard. */
    public static function dashboardRoles(): array
    {
        return self::staffEmailRoles();
    }

    /** Broad read access across staff portal modules. */
    public static function staffPortalReadRoles(): array
    {
        return self::staffEmailRoles();
    }

    /** Full operational managers (create/update/delete heavy modules). */
    public static function programAdministratorRoles(): array
    {
        return [self::INSTRUCTOR];
    }

    /** Roles that can manage document compliance and related operations. */
    public static function operationalManagerRoles(): array
    {
        return [self::INSTRUCTOR];
    }

    /** Roles that can view and act on the Document Queue (instructor + chairperson for forwarding). */
    public static function workflowQueueRoles(): array
    {
        return [self::INSTRUCTOR, self::CHAIRPERSON];
    }

    /** Roles that may open deployments index/show. */
    public static function deploymentViewerRoles(): array
    {
        return [
            self::INSTRUCTOR,
            self::CHAIRPERSON,
            self::DEAN,
        ];
    }

    /**
     * Student roster and profile (list/show). Excludes partner employers; they use workflow queue + document views.
     *
     * @return array<int, string>
     */
    public static function studentRegistryRoles(): array
    {
        return [
            self::INSTRUCTOR,
            self::CHAIRPERSON,
            self::DEAN,
        ];
    }

    /**
     * Institutional monitoring: compliance overview, evaluations, attendance log, PDF reports.
     * Excludes employers (company partners are not given school-wide student analytics).
     *
     * @return array<int, string>
     */
    public static function institutionalMonitoringRoles(): array
    {
        return [
            self::INSTRUCTOR,
            self::CHAIRPERSON,
            self::DEAN,
        ];
    }

    /**
     * Master required-document catalog (templates / ordering).
     *
     * @return array<int, string>
     */
    public static function requiredDocumentCatalogRoles(): array
    {
        return [
            self::INSTRUCTOR,
        ];
    }

    /** Roles that can view reports (aligned with institutional monitoring). */
    public static function reportsViewerRoles(): array
    {
        return self::institutionalMonitoringRoles();
    }

    /**
     * Roles that may create, edit, and delete announcements (staff-facing posts).
     */
    /** Roles that can view the announcements list (all staff). */
    public static function announcementViewerRoles(): array
    {
        return self::staffEmailRoles(); // instructor, chairperson, dean
    }

    /** Roles that can create/manage announcements. */
    public static function announcementManagerRoles(): array
    {
        return [
            self::INSTRUCTOR,
            self::CHAIRPERSON,
        ];
    }

    /** Allowed recipients when staff composes message threads. */
    public static function messageParticipantRolesForStaffSender(): array
    {
        return self::staffEmailRoles();
    }

    /** Allowed visible_to_role values for announcements. */
    public static function announcementVisibleToRuleValues(): array
    {
        return [
            'all',
            self::STUDENT_PORTAL,
            self::INSTRUCTOR,
            self::CHAIRPERSON,
            self::DEAN,
        ];
    }

    /**
     * Weekly journal viewer roles.
     */
    public static function weeklyJournalViewerRoles(): array
    {
        return [
            self::INSTRUCTOR,
        ];
    }

    /**
     * Daily Time Record viewer roles.
     */
    public static function dtrViewerRoles(): array
    {
        return [
            self::INSTRUCTOR,
        ];
    }

    /**
     * Certificate viewer roles.
     */
    public static function certificateViewerRoles(): array
    {
        return [
            self::INSTRUCTOR,
        ];
    }

    /**
     * Sidebar visibility for staff navigation (fine-grained overrides on top of route-based canView flags).
     *
     * @param  string  $item  One of: students, companies, deployments, required-documents, compliance, workflow-queue, attendance, evaluations, announcements, messages, reports, weekly-journals, certificates
     */
    public static function staffSidebarShows(string $role, string $item): bool
    {
        $role = strtolower(trim($role));

        $hidden = [
            // Instructor sees all — no hidden items.

            // Chairperson: program head — needs student roster + deployments + attendance oversight,
            // but not operational modules (companies, doc templates, weekly-journals, etc.).
            self::CHAIRPERSON => [
                'companies',
                'required-documents',
                'weekly-journals',
                'dtr',
                'certificates',
            ],

            // Dean: college-level oversight — students, attendance, evaluations, compliance, reports.
            // Workflow queue excluded — no documents assign dean as current_holder_role.
            // Operational modules hidden (companies, deployments, weekly-journals, etc.)
            self::DEAN => [
                'companies',
                'deployments',
                'required-documents',
                'weekly-journals',
                'dtr',
                'certificates',
                'workflow-queue',
            ],
        ];

        return ! in_array($item, $hidden[$role] ?? [], true);
    }
}
