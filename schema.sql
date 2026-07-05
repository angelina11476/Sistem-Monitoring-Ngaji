CREATE TABLE IF NOT EXISTS ustadz (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL,
    gelar TEXT NOT NULL,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'ustadz',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_ustadz_username ON ustadz(username);

CREATE TABLE IF NOT EXISTS anak (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL,
    level TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS biodata_santri (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    anak_id INTEGER NOT NULL,
    tempat_lahir TEXT,
    tanggal_lahir TEXT,
    nama_ayah TEXT,
    nama_ibu TEXT,
    alamat TEXT,
    no_telp TEXT,
    FOREIGN KEY (anak_id) REFERENCES anak(id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_biodata_santri_anak_id ON biodata_santri(anak_id);

CREATE TABLE IF NOT EXISTS progres (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    anak_id INTEGER NOT NULL,
    tanggal TEXT NOT NULL,
    juz INTEGER NOT NULL DEFAULT 0,
    surah TEXT NOT NULL,
    ayat INTEGER,
    halaman INTEGER,
    kelancaran INTEGER NOT NULL DEFAULT 3,
    catatan TEXT,
    durasi INTEGER,
    ustadz_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anak_id) REFERENCES anak(id) ON DELETE CASCADE,
    FOREIGN KEY (ustadz_id) REFERENCES ustadz(id)
);

CREATE TABLE IF NOT EXISTS presensi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    anak_id INTEGER NOT NULL,
    tanggal TEXT NOT NULL,
    status TEXT NOT NULL CHECK(status IN ('hadir', 'izin', 'alpa')),
    keterangan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anak_id) REFERENCES anak(id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_presensi_anak_tanggal ON presensi(anak_id, tanggal);
