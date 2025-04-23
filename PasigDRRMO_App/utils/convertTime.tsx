export function convertToPHT(utcDateString: string): string {
  const utcDate = new Date(utcDateString);

  // Format the date directly with the specified timezone
  return utcDate.toLocaleString("en-US", { timeZone: "Asia/Manila" });
}