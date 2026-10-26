-- ===========================================================================
-- VitroLab MX · esquema base
-- PostgreSQL 15+ (probado en Neon PostgreSQL 18)
-- Cultivo in vitro de material vegetal: casos, reportes, especies e
-- instituciones. Sin cédula profesional.
-- ===========================================================================

create extension if not exists citext;
create extension if not exists pg_trgm;

-- ---------------------------------------------------------------------------
-- Catálogos
-- ---------------------------------------------------------------------------
create table if not exists states (
    code varchar(5) primary key,
    name text not null,
    region text not null default 'Centro'
);

create table if not exists categories (
    id serial primary key,
    code text not null unique,
    name text not null,
    emoji text not null default '🌿',
    description text not null default '',
    sort_order int not null default 100
);

create table if not exists species (
    id serial primary key,
    slug text not null unique,
    scientific_name text not null,
    common_name text not null default '',
    family text not null default '',
    group_name text not null default 'Otros',
    description text not null default '',
    is_demo boolean not null default false,
    created_at timestamptz not null default now()
);

create index if not exists species_name_idx on species (scientific_name);
create index if not exists species_group_idx on species (group_name);

create table if not exists institutions (
    id serial primary key,
    name text not null,
    acronym text not null default '',
    slug text not null unique,
    state_code varchar(5) references states(code),
    type text not null default 'universidad' check (type in ('universidad', 'centro_conacyt', 'instituto', 'laboratorio', 'empresa', 'jardin_botanico', 'otro')),
    website text not null default '',
    email_domains text[] not null default '{}',
    description text not null default '',
    is_demo boolean not null default false,
    created_at timestamptz not null default now()
);

-- ---------------------------------------------------------------------------
-- Cuentas
-- ---------------------------------------------------------------------------
create table if not exists users (
    id bigserial primary key,
    name text not null,
    email citext not null unique,
    password_hash text not null,
    role text not null default 'estudiante' check (role in ('estudiante', 'docente', 'investigador', 'tecnico', 'editor', 'admin')),
    status text not null default 'pendiente' check (status in ('pendiente', 'verificado', 'suspendido')),
    institution_id int references institutions(id) on delete set null,
    institution_other text not null default '',
    career text not null default '',
    semester int check (semester between 1 and 12),
    student_id text not null default '',
    orcid text not null default '',
    state_code varchar(5) references states(code),
    bio text not null default '',
    avatar_url text,
    proof_path text,
    verification_method text not null default 'manual' check (verification_method in ('dominio_institucional', 'manual', 'admin')),
    verification_note text not null default '',
    terms_accepted_at timestamptz,
    email_verified boolean not null default false,
    last_login_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz
);

create index if not exists users_status_idx on users (status) where deleted_at is null;
create index if not exists users_institution_idx on users (institution_id);
create index if not exists users_role_idx on users (role);

-- ---------------------------------------------------------------------------
-- Casos de cultivo in vitro
-- ---------------------------------------------------------------------------
create table if not exists cases (
    id bigserial primary key,
    slug text not null unique,
    folio text not null unique,
    author_id bigint not null references users(id) on delete restrict,
    title text not null,
    summary text not null default '',
    category_id int references categories(id) on delete set null,
    species_id int references species(id) on delete set null,
    species_text text not null default '',
    state_code varchar(5) references states(code),
    municipality text not null default '',
    year int not null default extract(year from now())::int,
    objective text not null default '',
    context text not null default '',
    explant text not null default '',
    disinfection text not null default '',
    medium text not null default '',
    pgr text not null default '',
    conditions text not null default '',
    protocol text not null default '',
    results text not null default '',
    challenges text not null default '',
    lessons text not null default '',
    contamination_pct numeric(5,2),
    survival_pct numeric(5,2),
    multiplication_rate numeric(7,2),
    rooting_pct numeric(5,2),
    acclimatization_pct numeric(5,2),
    duration_weeks int,
    references_text text not null default '',
    license text not null default 'CC BY-NC-SA 4.0',
    status text not null default 'borrador' check (status in ('borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado')),
    featured boolean not null default false,
    views int not null default 0,
    is_demo boolean not null default false,
    published_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz,
    search_vector tsvector
);

create index if not exists cases_status_idx on cases (status, published_at desc) where deleted_at is null;
create index if not exists cases_category_idx on cases (category_id);
create index if not exists cases_species_idx on cases (species_id);
create index if not exists cases_state_idx on cases (state_code);
create index if not exists cases_search_idx on cases using gin (search_vector);
create index if not exists cases_title_trgm_idx on cases using gin (title gin_trgm_ops);
create index if not exists cases_author_idx on cases (author_id);
create index if not exists cases_featured_idx on cases (featured) where status = 'publicado';

create table if not exists case_images (
    id bigserial primary key,
    case_id bigint not null references cases(id) on delete cascade,
    path text not null,
    caption text not null default '',
    alt_text text not null default '',
    width int not null default 0,
    height int not null default 0,
    sort_order int not null default 0,
    created_at timestamptz not null default now()
);

create index if not exists case_images_case_idx on case_images (case_id, sort_order);

-- ---------------------------------------------------------------------------
-- Reportes de laboratorio
-- ---------------------------------------------------------------------------
create table if not exists reports (
    id bigserial primary key,
    slug text not null unique,
    folio text not null unique,
    author_id bigint not null references users(id) on delete restrict,
    case_id bigint references cases(id) on delete set null,
    title text not null,
    summary text not null default '',
    report_type text not null default 'ensayo' check (report_type in ('ensayo', 'protocolo', 'diagnostico', 'seguimiento', 'validacion', 'datos')),
    category_id int references categories(id) on delete set null,
    species_id int references species(id) on delete set null,
    species_text text not null default '',
    state_code varchar(5) references states(code),
    period_start date,
    period_end date,
    objective text not null default '',
    hypothesis text not null default '',
    design text not null default '',
    treatments_text text not null default '',
    replicates int,
    variables text not null default '',
    results text not null default '',
    analysis text not null default '',
    conclusions text not null default '',
    recommendations text not null default '',
    limitations text not null default '',
    references_text text not null default '',
    license text not null default 'CC BY-NC-SA 4.0',
    status text not null default 'borrador' check (status in ('borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado')),
    featured boolean not null default false,
    views int not null default 0,
    is_demo boolean not null default false,
    published_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz,
    search_vector tsvector
);

create index if not exists reports_status_idx on reports (status, published_at desc) where deleted_at is null;
create index if not exists reports_search_idx on reports using gin (search_vector);
create index if not exists reports_category_idx on reports (category_id);
create index if not exists reports_species_idx on reports (species_id);
create index if not exists reports_author_idx on reports (author_id);
create index if not exists reports_case_idx on reports (case_id);

create table if not exists report_treatments (
    id bigserial primary key,
    report_id bigint not null references reports(id) on delete cascade,
    label text not null,
    n int,
    response numeric(12,4),
    unit text not null default '',
    notes text not null default '',
    sort_order int not null default 0,
    created_at timestamptz not null default now()
);

create index if not exists report_treatments_report_idx on report_treatments (report_id, sort_order);

create table if not exists report_files (
    id bigserial primary key,
    report_id bigint not null references reports(id) on delete cascade,
    path text not null,
    original_name text not null,
    mime text not null,
    size_bytes bigint not null default 0,
    kind text not null default 'anexo',
    created_at timestamptz not null default now()
);

create index if not exists report_files_report_idx on report_files (report_id);

-- ---------------------------------------------------------------------------
-- Interacción comunitaria
-- ---------------------------------------------------------------------------
create table if not exists comments (
    id bigserial primary key,
    target_type text not null check (target_type in ('caso', 'reporte')),
    target_id bigint not null,
    user_id bigint not null references users(id) on delete cascade,
    body text not null,
    status text not null default 'visible' check (status in ('visible', 'oculto')),
    created_at timestamptz not null default now()
);

create index if not exists comments_target_idx on comments (target_type, target_id, created_at desc);
create index if not exists comments_user_idx on comments (user_id);

create table if not exists reactions (
    id bigserial primary key,
    target_type text not null check (target_type in ('caso', 'reporte')),
    target_id bigint not null,
    user_id bigint not null references users(id) on delete cascade,
    created_at timestamptz not null default now(),
    unique (target_type, target_id, user_id)
);

create index if not exists reactions_target_idx on reactions (target_type, target_id);

create table if not exists bookmarks (
    id bigserial primary key,
    target_type text not null check (target_type in ('caso', 'reporte')),
    target_id bigint not null,
    user_id bigint not null references users(id) on delete cascade,
    created_at timestamptz not null default now(),
    unique (target_type, target_id, user_id)
);

create index if not exists bookmarks_user_idx on bookmarks (user_id, created_at desc);

create table if not exists reviews (
    id bigserial primary key,
    target_type text not null check (target_type in ('caso', 'reporte')),
    target_id bigint not null,
    reviewer_id bigint not null references users(id) on delete restrict,
    decision text not null check (decision in ('publicar', 'cambios', 'rechazar')),
    notes text not null default '',
    created_at timestamptz not null default now()
);

create index if not exists reviews_target_idx on reviews (target_type, target_id, created_at desc);

-- ---------------------------------------------------------------------------
-- Seguridad y auditoría
-- ---------------------------------------------------------------------------
create table if not exists login_attempts (
    id bigserial primary key,
    email citext not null,
    ip inet,
    successful boolean not null default false,
    created_at timestamptz not null default now()
);

create index if not exists login_attempts_idx on login_attempts (created_at desc);

create table if not exists password_resets (
    id bigserial primary key,
    user_id bigint not null references users(id) on delete cascade,
    token_hash text not null,
    expires_at timestamptz not null,
    used_at timestamptz,
    created_at timestamptz not null default now()
);

create index if not exists password_resets_idx on password_resets (token_hash);

create table if not exists audit_log (
    id bigserial primary key,
    actor_id bigint references users(id) on delete set null,
    action text not null,
    entity_type text not null default '',
    entity_id bigint,
    meta jsonb not null default '{}'::jsonb,
    ip inet,
    created_at timestamptz not null default now()
);

create index if not exists audit_log_idx on audit_log (created_at desc);

-- ---------------------------------------------------------------------------
-- Búsqueda full-text y updated_at
-- ---------------------------------------------------------------------------
create or replace function cases_search_refresh() returns trigger
language plpgsql as $$
begin
    new.search_vector := to_tsvector('spanish',
        coalesce(new.title, '') || ' ' ||
        coalesce(new.summary, '') || ' ' ||
        coalesce(new.species_text, '') || ' ' ||
        coalesce(new.objective, '') || ' ' ||
        coalesce(new.context, '') || ' ' ||
        coalesce(new.explant, '') || ' ' ||
        coalesce(new.disinfection, '') || ' ' ||
        coalesce(new.medium, '') || ' ' ||
        coalesce(new.pgr, '') || ' ' ||
        coalesce(new.conditions, '') || ' ' ||
        coalesce(new.protocol, '') || ' ' ||
        coalesce(new.results, '') || ' ' ||
        coalesce(new.challenges, '') || ' ' ||
        coalesce(new.lessons, '') || ' ' ||
        coalesce(new.references_text, '')
    );
    return new;
end;
$$;

drop trigger if exists cases_search_trigger on cases;
create trigger cases_search_trigger
before insert or update on cases
for each row execute function cases_search_refresh();

create or replace function reports_search_refresh() returns trigger
language plpgsql as $$
begin
    new.search_vector := to_tsvector('spanish',
        coalesce(new.title, '') || ' ' ||
        coalesce(new.summary, '') || ' ' ||
        coalesce(new.species_text, '') || ' ' ||
        coalesce(new.objective, '') || ' ' ||
        coalesce(new.hypothesis, '') || ' ' ||
        coalesce(new.design, '') || ' ' ||
        coalesce(new.treatments_text, '') || ' ' ||
        coalesce(new.variables, '') || ' ' ||
        coalesce(new.results, '') || ' ' ||
        coalesce(new.analysis, '') || ' ' ||
        coalesce(new.conclusions, '') || ' ' ||
        coalesce(new.recommendations, '') || ' ' ||
        coalesce(new.limitations, '') || ' ' ||
        coalesce(new.references_text, '')
    );
    return new;
end;
$$;

drop trigger if exists reports_search_trigger on reports;
create trigger reports_search_trigger
before insert or update on reports
for each row execute function reports_search_refresh();

create or replace function touch_updated_at() returns trigger
language plpgsql as $$
begin
    new.updated_at := now();
    return new;
end;
$$;

drop trigger if exists users_touch on users;
create trigger users_touch before update on users
for each row execute function touch_updated_at();

drop trigger if exists cases_touch on cases;
create trigger cases_touch before update on cases
for each row execute function touch_updated_at();

drop trigger if exists reports_touch on reports;
create trigger reports_touch before update on reports
for each row execute function touch_updated_at();

-- Folios legibles por tipo de contenido
create sequence if not exists case_folio_seq start 1;
create sequence if not exists report_folio_seq start 1;
