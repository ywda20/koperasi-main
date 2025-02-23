ALTER TABLE
  `pinjaman`
ADD
  `keterangan` VARCHAR (255) NOT NULL
AFTER
  `status`;
ALTER TABLE
  `pinjaman_angsuran`
ADD
  `keterangan` VARCHAR (255) NOT NULL
AFTER
  `datetime`;
ALTER TABLE
  `shu_session`
ADD
  `keterangan` VARCHAR(255) NOT NULL
AFTER
  `status`;