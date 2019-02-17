create table fechamento_mensal(
id int (10),
mes_ano_referencia varchar(7),
saldo_caixa_especie decimal(13,2),
saldo_caixa decimal(13,2),
saldo_aplicacao decimal (13,2),
usuario int(11));
alter table fechamento_mensal add dt_fechamento datetime not null;

alter table caixa_movimento add id_fechamento_mensal int(10) not null;

ALTER TABLE caixa_movimento 
ADD INDEX fk_fechamento_mensal_idx (id_fechamento_mensal ASC);
ALTER TABLE caixa_movimento
ADD CONSTRAINT fk_fechamento_mensal
  FOREIGN KEY (id_fechamento_mensal)
  REFERENCES fechamento_mensal (id)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;


SELECT MONTH(STR_TO_DATE(dt_movimento, "%Y-%m-%d")) FROM caixa_movimento;

-- select * from caixa_movimento where dt_movimento > '2017-01-01';

update caixa_movimento set id_fechamento_mensal = 1 where dt_movimento < '2017-01-01' and cd_caixa_movimento < '14680';